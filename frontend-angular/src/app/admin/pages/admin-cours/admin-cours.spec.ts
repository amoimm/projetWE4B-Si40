import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminCours } from './admin-cours';

describe('AdminCours', () => {
  let component: AdminCours;
  let fixture: ComponentFixture<AdminCours>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminCours],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminCours);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
